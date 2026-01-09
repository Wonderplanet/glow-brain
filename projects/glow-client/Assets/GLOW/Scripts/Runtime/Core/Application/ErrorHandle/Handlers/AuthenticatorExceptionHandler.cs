using System;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate.Exceptions;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public class AuthenticatorExceptionHandler : IAuthenticatorExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }
        [Inject] IServerErrorExceptionPreHandler ServerErrorExceptionPreHandler { get; }
        [Inject] IServerErrorExceptionPostHandler ServerErrorExceptionPostHandler { get; }
        [Inject] INetworkExceptionHandler NetworkExceptionHandler { get; }

        public bool Handle(AuthenticatorException exception, Action completion)
        {
            // NOTE: AuthenticatorExceptionはInnerExceptionに発生した詳細のExceptionが格納されている
            //       そのためInnerExceptionを取得してハンドリングを行う
            switch (exception.InnerException)
            {
                case ServerErrorException see:
                    if (ServerErrorExceptionPreHandler.Handle(see, completion))
                    {
                        return true;
                    }

                    if (ServerErrorExceptionPostHandler.Handle(see, completion))
                    {
                        return true;
                    }
                    break;
                case NetworkException ne:
                    return NetworkExceptionHandler.Handle(ne, completion);
                default:
                    CommonExceptionViewer.Show(
                        Terms.Get("authenticator_error_dialog_title"),
                        Terms.Get("authenticator_error_dialog_message"),
                        exception,
                        completion);
                    break;
            }

            return true;
        }
    }
}
