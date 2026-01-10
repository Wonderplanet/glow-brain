using System;
using UnityHTTPLibrary.Authenticate.Exceptions;

namespace WPFramework.Application.ErrorHandle
{
    public interface IAuthenticatorExceptionHandler
    {
        bool Handle(AuthenticatorException exception, Action completion);
    }
}
