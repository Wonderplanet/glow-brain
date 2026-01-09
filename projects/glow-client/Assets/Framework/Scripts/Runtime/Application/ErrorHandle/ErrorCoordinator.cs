using System;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.ErrorCoordinator;
using WPFramework.Modules.Log;

namespace WPFramework.Application.ErrorHandle
{
    public class ErrorCoordinator : IErrorCoordinator
    {
        static ILogHandler _defaultLogHandler;

        readonly ICustomLogHandler _logHandler;

        bool _isDisposed;

        public ErrorCoordinator(ICustomLogHandler logHandler, IErrorHandler errorHandler)
        {
            _logHandler = logHandler;
            _logHandler.AddHandler(errorHandler);

            // NOTE: 初期セットされているエラーハンドラーを保持しておく
            _defaultLogHandler ??= Debug.unityLogger.logHandler;
        }

        public ErrorCoordinator(IErrorHandler errorHandler) : this(new DefaultErrorLogHandler(), errorHandler)
        {
        }

        public bool Initialize()
        {
            // NOTE: 既に違うログハンドラーが設定されていた場合失敗する
            //       初期設定されているハンドラーはinternalクラスなのでタイプ名を文字列でチェックする
            if (!Debug.unityLogger.logHandler.GetType().ToString().Equals("UnityEngine.DebugLogHandler"))
            {
                return false;
            }

            // NOTE: ログの出力に対してフィルターを設定する
            //       システムから出力されるログはError/Exceptionのみを出力するようにする
            //       https://docs.unity3d.com/ja/2022.3/ScriptReference/LogType.html
#if (UNITY_EDITOR || DEBUG) && !LOG_SUPPRESSION
            Debug.unityLogger.filterLogType = LogType.Log;
#else
            Debug.unityLogger.filterLogType = LogType.Error;
#endif // UNITY_EDITOR || DEBUG

            Debug.unityLogger.logHandler = _logHandler;
            // NOTE: 通常のログ出力に利用するハンドラーを渡す
            _logHandler.Initialize(_defaultLogHandler);

            // NOTE: UniTaskのエラー関連
            UniTaskScheduler.UnobservedTaskException += OnUniTaskUnobservedTaskException;
            UniTaskScheduler.PropagateOperationCanceledException = false;

            ApplicationLog.Log($"{nameof(ErrorCoordinator)}", $"filterLogType: {Debug.unityLogger.filterLogType}");

            return true;
        }

        public void AddHandler(IErrorHandler handler)
        {
            _logHandler.AddHandler(handler);
        }

        public void RemoveHandler(IErrorHandler handler)
        {
            _logHandler.RemoveHandler(handler);
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            UniTaskScheduler.UnobservedTaskException -= OnUniTaskUnobservedTaskException;

            _logHandler.Dispose();

            Debug.unityLogger.logHandler = _defaultLogHandler;
        }

        void OnUniTaskUnobservedTaskException(Exception exception)
        {
            _logHandler.LogException(exception, null);
        }
    }
}
