'''import json
import random
import numpy as np
import nltk
from nltk.stem import WordNetLemmatizer
from tensorflow.keras.models import load_model
import sys

# Initialize NLTK
lemmatizer = WordNetLemmatizer()
nltk.download('punkt')
nltk.download('wordnet')

# Load resources
model = load_model('chatbot_model.h5')
intents = json.loads(open('intents1.json').read())
words = json.loads(open('words.json').read())
classes = json.loads(open('classes.json').read())

# Preprocessing input
def clean_up_sentence(sentence):
    sentence_words = nltk.word_tokenize(sentence)
    sentence_words = [lemmatizer.lemmatize(word.lower()) for word in sentence_words]
    return sentence_words

def bow(sentence, words, show_details=True):
    sentence_words = clean_up_sentence(sentence)
    bag = [0] * len(words)
    for s in sentence_words:
        for i, w in enumerate(words):
            if w == s:
                bag[i] = 1
                if show_details:
                    print(f"found in bag: {w}")
    return np.array(bag)

# Predict intent
def predict_class(sentence, model):
    p = bow(sentence, words, show_details=False)
    res = model.predict(np.array([p]))[0]
    ERROR_THRESHOLD = 0.25
    results = [[i, r] for i, r in enumerate(res) if r > ERROR_THRESHOLD]
    results.sort(key=lambda x: x[1], reverse=True)
    return_list = []
    for r in results:
        return_list.append({"intent": classes[r[0]], "probability": str(r[1])})
    return return_list

# Get response
def get_response(ints, intents_json):
    if not ints:
        return "Sorry, I didn’t understand that."
    tag = ints[0]['intent']
    for i in intents_json['intents']:
        if i['tag'] == tag:
            return random.choice(i['responses'])
    return "Sorry, I didn’t understand that."

# === Input Handling: CLI or Web ===
if len(sys.argv) > 1:
    user_input = sys.argv[1]
else:
    user_input = sys.stdin.read().strip()

if not user_input:
    print("No input")
    sys.exit()

# === Run Chatbot ===
ints = predict_class(user_input, model)
res = get_response(ints, intents)
print(res)'''
