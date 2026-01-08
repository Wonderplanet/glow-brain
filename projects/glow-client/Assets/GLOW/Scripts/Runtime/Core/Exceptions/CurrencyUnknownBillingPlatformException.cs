using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyUnknownBillingPlatformException : WrappedServerErrorException
    {
        public CurrencyUnknownBillingPlatformException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
