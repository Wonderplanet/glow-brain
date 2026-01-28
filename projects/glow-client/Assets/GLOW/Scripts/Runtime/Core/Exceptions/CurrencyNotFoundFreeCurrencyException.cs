using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyNotFoundFreeCurrencyException : WrappedServerErrorException
    {
        public CurrencyNotFoundFreeCurrencyException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
