using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyNotEnoughCashException : WrappedServerErrorException
    {
        public CurrencyNotEnoughCashException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
