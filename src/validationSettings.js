export const ERROR_TYPES = Object.freeze({
  VALID: 0,
  REQUIRED: 1,
  VALUE: 2
})
export const ERROR_MESSAGES = {
  [ERROR_TYPES.VALID]: "No errors",
  [ERROR_TYPES.REQUIRED]: "Field required",
  [ERROR_TYPES.VALUE]: "Invalid input"
};
export const INPUT_TYPES = Object.freeze({
  TEXT: 0,
  EMAIL: 1,
  NUMBER: 2,
  DATE: 3,
})
export const VALIDATION_REGEX = {
  [INPUT_TYPES.TEXT]: new RegExp(/^[*]/),
  [INPUT_TYPES.EMAIL]: new RegExp(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/),
  [INPUT_TYPES.NUMBER]: new RegExp(/^[0-9]+$/),
  [INPUT_TYPES.DATE]: new RegExp(/((0[1-9]|[12]\d|3[01])\.(0[1-9]|1[0-2]|[1-9])\.(\d\d\d\d$|\d\d$))/)
}