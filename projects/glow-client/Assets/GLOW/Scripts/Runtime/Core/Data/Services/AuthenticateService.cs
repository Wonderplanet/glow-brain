using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate;
using UnityHTTPLibrary.Authenticate.Provider;
using UnityHTTPLibrary.Authenticate.Session;
using WPFramework.Domain.Models;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Modules.Authenticate;
using GLOW.Core.Modules.Authenticate.Provider;
using GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate;
using GLOW.Scenes.Title.Domains.Definition.Service;
using WPFramework.Domain.Modules;
using Zenject;
using WPFramework.Domain.Services;
using IAuthenticatorHostResolver = GLOW.Core.Domain.Resolvers.IAuthenticatorHostResolver;

namespace GLOW.Core.Data.Services
{
    public sealed class AuthenticateService : IOverrideAuthenticateTokenService
    {
        [Inject] IAuthenticatorHostResolver AuthenticatorHostResolver { get; }
        [Inject] IEnvironmentResolver EnvironmentResolver { get; }
        [Inject] IApiContextInitializer ApiContextInitializer { get; }
        [Inject] IServerErrorDelegate ServerErrorDelegate { get; }
        [Inject] ITimeOutDelegate TimeOutDelegate { get; }
        [Inject] ICommonRequestHeaderAssignor CommonRequestHeaderAssignor { get; }
        [Inject] IApiContextHeaderModifier ApiContextHeaderModifier { get; }

        async UniTask<AuthorizationModel> IAuthenticateService.Authenticate(CancellationToken cancellationToken)
        {
            return await Authenticate(cancellationToken, "");
        }

        public async UniTask<AuthorizationModel> Authenticate(
            CancellationToken cancellationToken,
            string deviceUniqueIdentifier)
        {
            using var authenticator = CreateOverrideAuthenticateToken();
            // NOTE: 認証処理を実行する
            var session = await authenticator.Authenticate(cancellationToken, deviceUniqueIdentifier);
            var sessionStore = new AuthenticateSessionStore(session);
            return new AuthorizationModel(sessionStore);
        }

        async UniTask IAuthenticateService.DeleteAuthenticationData(CancellationToken cancellationToken)
        {
            using var authenticator = CreateAuthenticator();
            // NOTE: 削除処理を実行する
            await authenticator.DeleteAuthenticationData();
        }

        bool IOverrideAuthenticateTokenService.ExistsToken()
        {
            using var authenticator = CreateOverrideAuthenticateToken();
            // NOTE: トークンが作成されているかを取得する
            return authenticator.ExistsToken();
        }

        async UniTask IOverrideAuthenticateTokenService.OverrideAuthenticateToken(
            CancellationToken cancellationToken,
            string token)
        {
            using var authenticator = CreateOverrideAuthenticateToken();
            // NOTE: トークンを上書きする
            await authenticator.OverrideAuthenticateToken(token);
        }

        IAuthenticator CreateAuthenticator()
        {
            var authenticateHost = AuthenticatorHostResolver.Resolve();
            var environment = EnvironmentResolver.Resolve();

            var apiContext = new ServerApi(authenticateHost.Uri);
            ApiContextInitializer.Initialize(apiContext, ApiContextInitializeSettings.Default);
            ApiContextHeaderModifier.Configure(apiContext);

            IAuthenticationProvider authenticationProvider = new GlowAuthenticationProvider(
                apiContext,
                authenticateHost.Password,
                environment.EnvironmentName);
            IAuthenticator authenticator = new GlowAuthenticator(authenticationProvider);
            return authenticator;
        }

        IOverrideAuthenticateToken CreateOverrideAuthenticateToken()
        {
            var authenticateHost = AuthenticatorHostResolver.Resolve();
            var environment = EnvironmentResolver.Resolve();

            var apiContext = new ServerApi(authenticateHost.Uri);
            ApiContextInitializer.Initialize(apiContext, ApiContextInitializeSettings.Default);
            ApiContextHeaderModifier.Configure(apiContext);

            IOverrideAuthenticateTokenProvider overrideAuthenticateTokenProvider = new GlowAuthenticationProvider(
                apiContext,
                authenticateHost.Password,
                environment.EnvironmentName);
            IOverrideAuthenticateToken overrideAuthenticateToken = new GlowAuthenticator(overrideAuthenticateTokenProvider);
            return overrideAuthenticateToken;
        }
    }
}
