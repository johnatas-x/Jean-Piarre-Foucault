from flask import Blueprint, render_template

blueprint = Blueprint("v4", __name__)

@blueprint.get("/")
def index():
  return render_template("v4.html")
