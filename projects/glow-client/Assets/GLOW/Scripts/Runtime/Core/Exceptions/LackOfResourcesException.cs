using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class LackOfResourcesException : DataInconsistencyServerErrorException
    {
        public LackOfResourcesException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
