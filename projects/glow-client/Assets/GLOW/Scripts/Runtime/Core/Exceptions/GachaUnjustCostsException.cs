using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaUnjustCostsException : WrappedServerErrorException
    {
        public GachaUnjustCostsException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
