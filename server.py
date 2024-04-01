from flask import Flask, send_file

app = Flask(__name__)

@app.route("/")
def serve_csv():
    log_file = "access_logs.csv"
    return send_file(log_file, as_attachment=True)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=80)
