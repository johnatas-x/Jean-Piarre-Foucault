from flask import Blueprint, render_template

blueprint = Blueprint("v3", __name__)

@blueprint.get("/")
def index():
  return render_template("v3.html")
