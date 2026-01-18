using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class EmblemNotOwnedException : DataInconsistencyServerErrorException
    {
        public EmblemNotOwnedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
