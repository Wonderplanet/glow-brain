using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions.CodeConversions
{
    public static class ServerErrorConverter
    {
        public static string ConvertToInquiryId(ServerErrorException exception)
        {
            if (exception == null)
            {
                return ConvertToInquiryId(0);
            }

            // NOTE: StatusCodeが299の場合はServerErrorExceptionのServerErrorCodeを使用する
            return exception.StatusCode == HTTPStatusCodes.ApplicationError ?
                ConvertToInquiryId(exception.ServerErrorCode) :
                HttpErrorConverter.ConvertToInquiryId(exception.StatusCode);
        }

        static string ConvertToInquiryId(int serverErrorCode)
        {
            return $"SRV-{serverErrorCode}";
        }
    }
}
