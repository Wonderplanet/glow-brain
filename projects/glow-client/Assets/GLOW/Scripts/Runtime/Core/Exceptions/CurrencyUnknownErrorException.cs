using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyUnknownErrorException : WrappedServerErrorException
    {
        public CurrencyUnknownErrorException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
