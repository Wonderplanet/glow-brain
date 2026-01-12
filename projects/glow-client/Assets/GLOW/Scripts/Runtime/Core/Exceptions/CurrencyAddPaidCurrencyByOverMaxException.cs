using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyAddPaidCurrencyByOverMaxException : WrappedServerErrorException
    {
        public CurrencyAddPaidCurrencyByOverMaxException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}