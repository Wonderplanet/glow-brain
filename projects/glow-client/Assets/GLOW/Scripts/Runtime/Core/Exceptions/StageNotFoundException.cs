using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageNotFoundException : DataInconsistencyServerErrorException
    {
        public StageNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
