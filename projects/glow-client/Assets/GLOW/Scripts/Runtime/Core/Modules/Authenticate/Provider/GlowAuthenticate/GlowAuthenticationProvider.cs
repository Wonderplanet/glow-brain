using System.Text;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Authenticate.Log;
using GLOW.Core.Modules.Authenticate.Token;
using Newtonsoft.Json;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate.Provider;
using UnityHTTPLibrary.Authenticate.Session;

namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    public class GlowAuthenticationProvider : IOverrideAuthenticateTokenProvider
    {
        public string Identifier => nameof(GlowAuthenticationProvider);

        ServerApi ApiContext
        {
            get;
        }

        GlowIDTokenStorage IDTokenStorage
        {
            get;
        }

        readonly string _storagePassword;
        readonly IWPAuthenticationEndPointPath _authenticationEndPointPath;

        public GlowAuthenticationProvider(ServerApi apiContext, string storagePassword, string environmentIdentifier) :
            this(apiContext, storagePassword, environmentIdentifier, new WPDefaultAuthenticationEndPointPath())
        {
        }

        public GlowAuthenticationProvider(ServerApi apiContext, string storagePassword, string environmentIdentifier, IWPAuthenticationEndPointPath authenticationEndPointPath)
        {
            ApiContext = apiContext;

            IDTokenStorage = new GlowIDTokenStorage(environmentIdentifier);
            _storagePassword = storagePassword;

            _authenticationEndPointPath = authenticationEndPointPath;
        }

        async UniTask<ApiSession> IAuthenticationProvider.Authenticate(CancellationToken cancellationToken, object optionalData)
        {
            return await Authenticate(cancellationToken, "", optionalData);
        }

        public async UniTask<ApiSession> Authenticate(
            CancellationToken cancellationToken,
            string deviceUniqueIdentifier, 
            object optionalData)
        {
            var signupData = await SignupOrLoadIDToken(
                cancellationToken, 
                _storagePassword, 
                deviceUniqueIdentifier,
                optionalData);
                
            var signinData = await Signin(cancellationToken, signupData.IdToken, optionalData);

            var session = new ApiSession(signinData.AccessToken);
            return session;
        }

        async UniTask<GlowSigninData> Signin(CancellationToken cancellationToken, string idToken, object optionalData)
        {
            var requestData = new GlowSigninRequestData(idToken);

            var payload = new Payload
            {
                ContentType = MimeTypes.Json,
                Data = Encoding.UTF8.GetBytes(JsonConvert.SerializeObject(requestData))
            };

            var response = await ApiContext.Request<GlowSigninResponse>(
                cancellationToken,
                _authenticationEndPointPath.SignInPath,
                HTTPMethods.Post,
                payload,
                null,
                optionalData);

            return new GlowSigninData(response.AccessToken);
        }

        async UniTask<GlowSignupData> SignupOrLoadIDToken(
            CancellationToken cancellationToken, 
            string idTokenPassword,
            string deviceUniqueIdentifier, 
            object optionalData)
        {
            // NOTE: idTokenはストレージに保持しており、存在しているのであればSignUp済みとして扱う
            string idToken;
            string identifier;
            if (!IDTokenStorage.Exists())
            {
                GlowAuthenticationLogger.Log($"Signupを行います");
                // NOTE: 認証サーバーに問い合わせを行う
                var response = await SignupRequest(cancellationToken, deviceUniqueIdentifier, optionalData);
                idToken = response.IDToken;
                identifier = Identifier;
                IDTokenStorage.Write(idToken, identifier, idTokenPassword);
            }
            else
            {
                GlowAuthenticationLogger.Log($"Signup情報を読み込みます");
                var tokenData = IDTokenStorage.Read(idTokenPassword);
                idToken = tokenData.IDToken;
                identifier = tokenData.Identifier;
            }

            return new GlowSignupData(idToken, identifier);
        }

        async UniTask<GlowSignupResponse> SignupRequest(
            CancellationToken cancellationToken, 
            string deviceUniqueIdentifier, 
            object optionalData)
        {
            var requestData = new GlowSignupRequestData(deviceUniqueIdentifier);
        
            var payload = new Payload
            {
                ContentType = MimeTypes.Json,
                Data = Encoding.UTF8.GetBytes(JsonConvert.SerializeObject(requestData))
            };

            return await ApiContext.Request<GlowSignupResponse>(
                cancellationToken,
                _authenticationEndPointPath.SignUpPath,
                HTTPMethods.Post,
                payload,
                null,
                optionalData);
        }

        async UniTask IAuthenticationProvider.DeleteAuthenticationData()
        {
            await UniTask.FromResult(0);

            IDTokenStorage.Delete();
        }

        bool IOverrideAuthenticateTokenProvider.ExistsToken()
        {
            return IDTokenStorage.Exists();
        }

        async UniTask IOverrideAuthenticateTokenProvider.OverrideAuthenticateToken(string token)
        {
            await UniTask.FromResult(0);

            IDTokenStorage.Delete();
            IDTokenStorage.Write(token, Identifier, _storagePassword);
        }
    }
}
