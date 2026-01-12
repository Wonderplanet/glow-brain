#if OBSERVABILITY_DATADOG_ENABLED
using System;
using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Networking;
using UnityHTTPLibrary;
using UnityHTTPLibrary.UnityWebRequestImpl;
using UnityHTTPLibrary.Utility;
using UnityWebRequest = UnityEngine.Networking.UnityWebRequest;

namespace WPFramework.Modules.Observability
{
    public sealed class DatadogTrackedWebRequest : IHTTPRequest
    {
        const string UndefinedContentType = "application/x-www-form-urlencoded";

        readonly UnityWebRequest _webRequest;
        WWWForm _fields;
        MimeTypes _mimeType;
        bool _used;
        readonly DatadogWebRequestTracker _datadogWebRequestTracker = new DatadogWebRequestTracker();

        public HTTPRequestStatusCodes StatusCode { get; private set; } = HTTPRequestStatusCodes.Initial;

        public HTTPMethods Method
        {
            get => UnityWebRequestUtility.ToHttpMethods(_webRequest.method);
            set => _webRequest.method = UnityWebRequestUtility.ToStringMethods(value);
        }

        public MimeTypes MimeType
        {
            get => _mimeType;
            set
            {
                _mimeType = value;
                SetHeader(Header.ContentType,
                    value == MimeTypes.Undefined
                        ? UndefinedContentType
                        : MimeTypeUtility.MimeTypeToContentType(value));
            }
        }

        public Uri Uri => _webRequest.uri;

        public WWWForm Fields
        {
            get => _fields;
            set
            {
                _fields = value;
                if (value != null)
                {
                    Data = value.data;
                }
            }
        }

        public int RequestTimeout {
            get => _webRequest.timeout;
            set => _webRequest.timeout = value;
        }
        public HTTPProgressData ProgressData { get; private set; }
        public HTTPResponseData Response { get; private set; }
        public Dictionary<string, string> Headers { get; } = new ();

        public bool IsCompleted => StatusCode >= HTTPRequestStatusCodes.Success;

        public Exception Exception { get; private set; }

        object IEnumerator.Current => null;

        public byte[] Data
        {
            set => _webRequest.uploadHandler = new UploadHandlerRaw(value);
            get => _webRequest.uploadHandler?.data;
        }

        public DatadogTrackedWebRequest(Uri uri, ITLSCertificateHandler certificateHandler = null)
        {
            _webRequest = new UnityWebRequest(uri);
            if (certificateHandler != null)
            {
                _webRequest.certificateHandler = new UnityWebRequestCertificateHandler(uri.Host, certificateHandler);
            }
            _webRequest.disposeUploadHandlerOnDispose = true;
            _webRequest.disposeDownloadHandlerOnDispose = true;
        }

        bool IEnumerator.MoveNext()
        {
            if (IsCompleted)
            {
                return false;
            }

            // NOTE: ダウンロードサイズを取得
            var contentLength = GetContentLength();

            OnRequestProgress(
                new HTTPProgressData()
                {
                    Downloaded = (long)(100 * _webRequest.downloadProgress),
                    Total = contentLength,
                });

            if (_webRequest.isDone)
            {
                OnRequestComplete();
                return false;
            }

            return StatusCode < HTTPRequestStatusCodes.Success;
        }

        long GetContentLength()
        {
            // NOTE: ダウンロードサイズを取得
            //       ダウンロードサイズが取得できない場合は0を設定
            //       GetResponseHeader は小文字大文字の区別がないため、Content-Length と content-length は同じ値を取得する
            var contentLengthValue = _webRequest.GetResponseHeader(Header.ContentLength);
            if (string.IsNullOrEmpty(contentLengthValue))
            {
                return 0L;
            }

            return !long.TryParse(contentLengthValue, out var contentLength) ? 0L : contentLength;
        }

        void IEnumerator.Reset()
        {
            throw new NotImplementedException();
        }

        public string FullUrl()
        {
            var fullUrl = Uri.ToString();
            return fullUrl;
        }

        public void SetHeader(string key, string value)
        {
            Headers.Add(key, value);
            _webRequest.SetRequestHeader(key, value);
        }

        IHTTPRequest IHTTPRequest.Send()
        {
            if (_used)
            {
                throw new Exception("HTTPRequest is already used.");
            }

            _used = true;
            _webRequest.downloadHandler = new DownloadHandlerBuffer();
            _webRequest.SendWebRequest();
            StatusCode = HTTPRequestStatusCodes.Processing;

            _datadogWebRequestTracker.StartResource(_webRequest);

            return this;
        }

        void IHTTPRequest.Abort()
        {
            Abort();
        }

        void IDisposable.Dispose()
        {
            if (StatusCode == HTTPRequestStatusCodes.Disposed) return;

            Abort();
            _webRequest.certificateHandler?.Dispose();
            _webRequest.Dispose();
            StatusCode = HTTPRequestStatusCodes.Disposed;
        }

        void Abort()
        {
            if (StatusCode == HTTPRequestStatusCodes.Aborted)
            {
                return;
            }

            if (StatusCode == HTTPRequestStatusCodes.Processing)
            {
                _webRequest.Abort();
                StatusCode = HTTPRequestStatusCodes.Aborted;
            }
        }

        void OnRequestComplete()
        {
            // https://docs.unity3d.com/ja/2022.3/ScriptReference/Networking.UnityWebRequest.Result.html
            if (_webRequest.result == UnityWebRequest.Result.Success)
            {
                Response = new HTTPResponseData(
                    _webRequest.GetResponseHeaders(),
                    HTTPResponseStatusCodesTranslator.Translate((int)_webRequest.responseCode),
                    _webRequest.downloadHandler.data);

                StatusCode = HTTPRequestStatusCodes.Success;
            }
            else if (_webRequest.result == UnityWebRequest.Result.ProtocolError)
            {
                Response = new HTTPResponseData(
                    _webRequest.GetResponseHeaders(),
                    HTTPResponseStatusCodesTranslator.Translate((int)_webRequest.responseCode),
                    _webRequest.downloadHandler.data);

                StatusCode = HTTPRequestStatusCodes.Error;
            }
            else if (
                _webRequest.result == UnityWebRequest.Result.ConnectionError ||
                _webRequest.result == UnityWebRequest.Result.DataProcessingError ||
                string.IsNullOrEmpty(_webRequest.error))
            {
                if (StatusCode == HTTPRequestStatusCodes.Aborted) return;

                // NOTE: タイムアウトの場合はエラーメッセージをフック
                if (UnityWebRequestUtility.IsTimeoutError(_webRequest))
                {
                    StatusCode = HTTPRequestStatusCodes.Timeout;
                    return;
                }
                // NOTE: SSL CA certificate error の場合はエラーメッセージをフック
                if (UnityWebRequestUtility.IsTLSCertificateError(_webRequest))
                {
                    Exception = new TLSCertificateValidationException(_webRequest.error);
                    StatusCode = HTTPRequestStatusCodes.RequestError;
                    return;
                }

                Exception = new Exception(_webRequest.error);
                StatusCode = HTTPRequestStatusCodes.RequestError;
            }
            else
            {
                Exception = new Exception("request invalid");
                StatusCode = HTTPRequestStatusCodes.RequestError;
            }

            _datadogWebRequestTracker.StopResource(_webRequest);
        }

        void OnRequestProgress(HTTPProgressData progressData)
        {
            ProgressData = progressData;
        }
    }
}
#endif  // OBSERVABILITY_DATADOG_ENABLED
