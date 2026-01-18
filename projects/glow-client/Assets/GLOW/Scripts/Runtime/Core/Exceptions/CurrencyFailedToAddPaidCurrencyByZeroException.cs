using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyFailedToAddPaidCurrencyByZeroException : WrappedServerErrorException
    {
        public CurrencyFailedToAddPaidCurrencyByZeroException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
