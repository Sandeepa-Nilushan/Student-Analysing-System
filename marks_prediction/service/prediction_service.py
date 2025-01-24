import joblib
from sklearn.impute import SimpleImputer
import pandas as pd

def load_model():
    return joblib.load('models/model.pkl')


def preproces(marks):

    data = pd.read_csv('models/Student HUB.csv')
    X = data.drop(['Progress'], axis=1)
    data.dropna(axis=1, how='all', inplace=True)

    imputer = SimpleImputer(strategy='mean')
    X = imputer.fit_transform(X)

    total_marks = float(marks)
    student_data = pd.DataFrame(columns=data.columns[:-2])
    student_data.loc[0, 'Total'] = total_marks
    student_data = imputer.transform(student_data)

    return student_data

def start_prediction(marks):
    model = load_model()
    p_marks = preproces(marks)
    output = model.predict(p_marks)
    return output[0]