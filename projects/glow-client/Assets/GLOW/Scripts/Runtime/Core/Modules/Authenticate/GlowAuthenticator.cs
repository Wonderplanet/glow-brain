using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Authenticate.Log;
using GLOW.Core.Modules.Authenticate.Provider;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate;
using UnityHTTPLibrary.Authenticate.Exceptions;
using UnityHTTPLibrary.Authenticate.Provider;
using UnityHTTPLibrary.Authenticate.Session;

namespace GLOW.Core.Modules.Authenticate
{
    public class GlowAuthenticator :IOverrideAuthenticateToken
    {
        bool _disposed;

        public ServerApi ServerApi
        {
            get;
        }

        public ApiSession Session
        {
            get;
            private set;
        }

        public ISessionStore SessionStore
        {
            get;
        }

        public bool IsAuthorized => Session != null;

        readonly IAuthenticationProvider _authenticationProvider;

        readonly IOverrideAuthenticateTokenProvider _overrideAuthenticateTokenProvider;

        readonly CancellationTokenSource _disposeCancellationTokenSource = new CancellationTokenSource();

        /// <summary>
        /// コンストラクタ
        /// </summary>
        /// <param name="host">認証先のホスト</param>
        /// <param name="storagePassword">認証情報保存時のパスワード</param>
        /// <param name="environmentIdentifier">環境識別子</param>
        /// <param name="externalSessionStore">セッション情報の格納先</param>
        /// <remarks>
        /// externalSessionStoreに値が設定されていなかった場合に
        /// AuthenticateSessionStore を自動的に生成します。
        /// </remarks>
        public GlowAuthenticator(string host, string storagePassword, string environmentIdentifier, ISessionStore externalSessionStore = null) :
            this(new ServerApi(host), storagePassword, environmentIdentifier, externalSessionStore)
        {
        }

        /// <summary>
        /// コンストラクタ
        /// </summary>
        /// <param name="apiContext">接続時に利用するServerApi</param>
        /// <param name="storagePassword">認証情報保存時のパスワード</param>
        /// <param name="environmentIdentifier">環境識別子</param>
        /// <param name="externalSessionStore">セッション情報の格納先</param>
        /// <remarks>
        /// externalSessionStoreに値が設定されていなかった場合に
        /// AuthenticateSessionStore を自動的に生成します。
        /// </remarks>
        public GlowAuthenticator(ServerApi apiContext, string storagePassword, string environmentIdentifier, ISessionStore externalSessionStore = null) :
            this(new WPAuthenticationProvider(apiContext, storagePassword, environmentIdentifier), externalSessionStore)
        {
            ServerApi = apiContext;

            GlowAuthenticationLogger.Log($"{environmentIdentifier}環境が設定されました");
        }

        /// <summary>
        /// コンストラクタ
        /// </summary>
        /// <param name="authenticationProvider">利用する認証処理</param>
        /// <param name="externalSessionStore">セッション情報の格納先</param>
        /// <remarks>
        /// externalSessionStoreに値が設定されていなかった場合に
        /// AuthenticateSessionStore を自動的に生成します。
        /// </remarks>
        public GlowAuthenticator(IAuthenticationProvider authenticationProvider, ISessionStore externalSessionStore = null)
        {
            _authenticationProvider = authenticationProvider;

            // NOTE: 外部より注入された場合は注入先のSessionStoreに対して情報をセットする
            SessionStore = externalSessionStore ?? new AuthenticateSessionStore(null);
        }

        public GlowAuthenticator(IOverrideAuthenticateTokenProvider overrideAuthenticateTokenProvider, ISessionStore externalSessionStore = null)
        {
            _overrideAuthenticateTokenProvider = overrideAuthenticateTokenProvider;

            // NOTE: 外部より注入された場合は注入先のSessionStoreに対して情報をセットする
            SessionStore = externalSessionStore ?? new AuthenticateSessionStore(null);
        }

        public async UniTask<ApiSession> Authenticate(
            CancellationToken cancellationToken,
            object optionalData)
        {
            return await Authenticate(cancellationToken, "", optionalData);
        }

        public async UniTask<ApiSession> Authenticate(
            CancellationToken cancellationToken, 
            string deviceUniqueIdentifier, 
            object optionalData)
        {
            using var cancellationTokenSource =
                CancellationTokenSource.CreateLinkedTokenSource(_disposeCancellationTokenSource.Token, cancellationToken);

            try
            {
                Session = await _overrideAuthenticateTokenProvider.Authenticate(
                    cancellationTokenSource.Token,
                    deviceUniqueIdentifier,
                    optionalData);
                
                // NOTE: Sessionを取得した後に必ずSessionStoreにセットし直す
                //       SessionStoreの参照を共有しておけばSessionを再取得した場合に必ず最新のものがセットされる
                SessionStore.SetApiSession(Session);
            }
            catch (OperationCanceledException)
            {
                // NOTE: 認証中のキャンセルリクエストはAuthenticatorExceptionとして取り扱わない
                //       サーバーエラーかつハンドリングされたものはAuthenticatorExceptionとして取り扱う
                throw;
            }
            catch (Exception e)
            {
                throw new AuthenticatorException(e.Message, e);
            }

            return Session;
        }

        public async UniTask DeleteAuthenticationData()
        {
            await _authenticationProvider.DeleteAuthenticationData();
            Session = null;
        }

        bool IOverrideAuthenticateToken.ExistsToken()
        {
            return _overrideAuthenticateTokenProvider.ExistsToken();
        }

        async UniTask IOverrideAuthenticateToken.OverrideAuthenticateToken(string token)
        {
            await _overrideAuthenticateTokenProvider.OverrideAuthenticateToken(token);
        }

        public void Dispose()
        {
            if (_disposed)
            {
                return;
            }

            _disposeCancellationTokenSource.Cancel();
            _disposeCancellationTokenSource.Dispose();

            _disposed = true;

            Session = null;
        }
    }
}
