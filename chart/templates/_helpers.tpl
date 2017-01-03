{{/* vim: set filetype=mustache: */}}

{{- define "name" -}}
  {{- default .Chart.Name .Values.nameOverride | trunc 24 -}}
{{- end -}}

{{- define "fullname" -}}
  {{- $name := default .Chart.Name .Values.nameOverride -}}
  {{- printf "%s-%s" .Release.Name $name | trunc 24 -}}
{{- end -}}
