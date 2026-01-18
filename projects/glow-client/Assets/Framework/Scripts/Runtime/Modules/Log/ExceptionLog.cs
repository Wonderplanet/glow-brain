using System;
using System.Text;

namespace WPFramework.Modules.Log
{
    public static class ExceptionLog
    {
        public static string BuildStackTrace(Exception exception)
        {
            if (exception == null)
            {
                return string.Empty;
            }

            var builder = new StringBuilder();

            // NOTE: Exceptionの情報を文字列に変換するロジックをここに実装する
            builder.AppendLine($"{exception.GetType().FullName}: {exception.Message}");

            // StackTraceを追加
            if (exception.StackTrace != null)
            {
                builder.AppendLine("StackTrace: " + exception.StackTrace);
            }

            // NOTE: InnerExceptionがあれば再帰的に処理して追加する
            if (exception.InnerException == null)
            {
                return builder.ToString();
            }

            var innerExceptionString = BuildStackTrace(exception.InnerException);
            builder.AppendLine("Inner Exception: " + innerExceptionString);

            return builder.ToString();
        }
    }
}
