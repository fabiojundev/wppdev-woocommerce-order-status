"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
exports.__esModule = true;
var react_1 = __importDefault(require("react"));
var Status = function (props) {
    var s = { id: 1 };
    var id = s.id, name = s.name, slug = s.slug;
    return (react_1["default"].createElement(react_1["default"].Fragment, null,
        react_1["default"].createElement("div", null, id),
        react_1["default"].createElement("div", null, name),
        react_1["default"].createElement("div", null, slug)));
};
exports["default"] = Status;
