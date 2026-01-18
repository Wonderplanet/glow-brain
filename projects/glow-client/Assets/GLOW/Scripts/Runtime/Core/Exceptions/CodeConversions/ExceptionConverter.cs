using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Sockets;
using System.Reflection;
using UnityEngine.AddressableAssets;
using UnityEngine.Localization.SmartFormat.Core.Formatting;
using UnityEngine.Localization.SmartFormat.Core.Parsing;
using UnityEngine.ResourceManagement.Exceptions;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate.Exceptions;
using WPFramework.Exceptions;

namespace GLOW.Core.Exceptions.CodeConversions
{
    public static class ExceptionConverter
    {
        public static string ConvertToInquiryId(Exception exception)
        {
            if (exception is ServerErrorException see)
            {
                return ServerErrorConverter.ConvertToInquiryId(see);
            }
            if (exception is SocketException se && NetworkErrorConverter.HasTarget(se))
            {
                return NetworkErrorConverter.ConvertToInquiryId(se);
            }
            if (exception is NetworkException ne && NetworkErrorConverter.HasTarget(ne))
            {
                return NetworkErrorConverter.ConvertToInquiryId(ne);
            }

            var code = 0;
            if (exception == null)
            {
                return FormattedInquiryId(code);
            }

            code = exception switch
            {
                NullReferenceException => 2,
                ArgumentNullException => 3,
                ArgumentOutOfRangeException => 4,
                ArgumentException => 5,
                IndexOutOfRangeException => 6,
                UriFormatException => 7,
                FormatException => 8,
                ObjectDisposedException => 9,
                InvalidOperationException => 10,
                FileNotFoundException => 11,
                DivideByZeroException => 12,
                DiskFullException => 13,
                InvalidKeyException => 14,
                AuthenticatorException => 15,
                ServerResponseParseException => 16,
                NetworkException => 17,
                OverflowException => 18,
                KeyNotFoundException => 19,
                PathTooLongException => 20,
                PlatformNotSupportedException => 21,
                RankException => 22,
                TimeoutException => 23,
                NotImplementedException => 24,
                DriveNotFoundException => 25,
                DirectoryNotFoundException => 26,
                UnknownResourceProviderException => 27,
                ResourceManagerException => 28,
                AmbiguousMatchException => 29,
                FormattingException => 30,
                ParsingErrors => 31,
                _ => 1
            };

            return FormattedInquiryId(code);
        }

        static string FormattedInquiryId(int code)
        {
            return $"CLE-{code}";
        }
    }
}
