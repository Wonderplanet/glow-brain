using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyAddFreeCurrencyByOverMaxException : WrappedServerErrorException
    {
        public CurrencyAddFreeCurrencyByOverMaxException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}