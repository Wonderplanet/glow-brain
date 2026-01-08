using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MissionCannotClearException : WrappedServerErrorException
    {
        public MissionCannotClearException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
