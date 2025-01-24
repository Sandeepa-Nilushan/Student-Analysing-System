from flask import Flask, request, jsonify
from flask_cors import CORS
from flask_cors import cross_origin
from service.prediction_service import start_prediction

app = Flask(__name__)
CORS(app)  # Enable CORS for all origins

@app.route('/predict', methods=['POST'])
@cross_origin()
def detect_objects():
    total_marks = float(request.form.get("total_marks")) # give total marks
    prediction = start_prediction(total_marks)

    result = {
        "header" : {"total_marks" : total_marks},
        "predictions" : prediction
    }

    return jsonify({'results': result})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8081, debug=True)
