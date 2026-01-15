using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyAddCurrencyByOverMaxException : WrappedServerErrorException
    {
        public CurrencyAddCurrencyByOverMaxException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
