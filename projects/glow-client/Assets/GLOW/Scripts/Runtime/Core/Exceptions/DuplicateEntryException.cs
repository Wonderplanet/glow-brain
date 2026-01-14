using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class DuplicateEntryException : DataInconsistencyServerErrorException
    {
        public DuplicateEntryException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
