using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using UnityHTTPLibrary;

namespace WPFramework.Data.Extensions
{
    public static class UnityHTTPLibraryExtension
    {
        public static async UniTask<T> Post<T>(this ServerApi api, CancellationToken cancellationToken, string path, Payload param, IProgress<HTTPProgressData> progressReporter = null, object optionalData = null)
        {
            return await api.Request<T>(cancellationToken, path, HTTPMethods.Post, param);
        }

        public static async UniTask<T> Post<T>(this ServerApi api, CancellationToken cancellationToken, string path, WWWForm param, IProgress<HTTPProgressData> progressReporter = null, object optionalData = null)
        {
            return await api.Request<T>(cancellationToken, path, HTTPMethods.Post, param);
        }

        public static async UniTask<T> Get<T>(this ServerApi api, CancellationToken cancellationToken, string path, Payload param, IProgress<HTTPProgressData> progressReporter = null, object optionalData = null)
        {
            return await api.Request<T>(cancellationToken, path, HTTPMethods.Get, param);
        }

        public static async UniTask<T> Get<T>(this ServerApi api, CancellationToken cancellationToken, string path, WWWForm param, IProgress<HTTPProgressData> progressReporter = null, object optionalData = null)
        {
            return await api.Request<T>(cancellationToken, path, HTTPMethods.Get, param);
        }
    }
}
