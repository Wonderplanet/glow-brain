using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyInvalidDebugEnvironmentException : WrappedServerErrorException
    {
        public CurrencyInvalidDebugEnvironmentException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
