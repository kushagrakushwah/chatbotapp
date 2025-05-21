#!/usr/bin/env python3
import sys
import json
import pickle
import numpy as np
from tensorflow.keras.models import load_model

# ————————————————————————————————
# 1) Load your artifacts (from the `model/` subfolder)
# ———————————————————————————————— 
INTENTS_FILE   = "intents1.json"
WORDS_PKL      = "model/words.pkl"
CLASSES_PKL    = "model/classes.pkl"
MODEL_FILE     = "model/chatbot_model.keras"
ERROR_THRESHOLD = 0.25

# load once at startup
intents = json.load(open(INTENTS_FILE,   "r", encoding="utf8"))
words   = pickle.load(open(WORDS_PKL,     "rb"))
classes = pickle.load(open(CLASSES_PKL,   "rb"))
model   = load_model(MODEL_FILE)


# ————————————————————————————————
# 2) Text → “bag-of-words” vector
# ————————————————————————————————
def clean_up_sentence(sentence):
    return sentence.lower().split()

def bag_of_words(sentence, words_list):
    sentence_words = clean_up_sentence(sentence)
    bag = [0] * len(words_list)
    for s in sentence_words:
        if s in words_list:
            bag[words_list.index(s)] = 1
    return np.array(bag)


# ————————————————————————————————
# 3) Run through the network & threshold
# ————————————————————————————————
def predict_class(sentence):
    bow = bag_of_words(sentence, words)
    probs = model.predict(np.array([bow]), verbose=0)[0]
    # only keep predictions above threshold
    filtered = [(i, p) for i, p in enumerate(probs) if p > ERROR_THRESHOLD]
    filtered.sort(key=lambda x: x[1], reverse=True)
    return [{"intent": classes[i], "probability": str(p)} for i, p in filtered]


# ————————————————————————————————
# 4) Pick a random response from that intent
# ————————————————————————————————
def get_response(ints, intents_json):
    if not ints:
        return "Sorry, I didn’t understand that."
    tag = ints[0]["intent"]
    # find the matching intent in your JSON
    for intent in intents_json["intents"]:
        if intent["tag"] == tag:
            return np.random.choice(intent["responses"])
    return "Sorry, I didn’t understand that."


# ————————————————————————————————
# 5) Entry-point: read the user’s message, predict, print JSON
# ————————————————————————————————
if __name__ == "__main__":
    # prefer CLI arg over STDIN
    if len(sys.argv) > 1:
        user_input = " ".join(sys.argv[1:]).strip()
    else:
        user_input = sys.stdin.read().strip()

    if not user_input:
        print(json.dumps({"error": "no input"}))
        sys.exit(1)

    ints = predict_class(user_input)
    res  = get_response(ints, intents)
    print(json.dumps({"message": res}))
