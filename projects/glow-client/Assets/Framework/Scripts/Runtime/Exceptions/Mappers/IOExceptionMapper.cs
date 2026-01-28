using System;
using System.IO;

namespace WPFramework.Exceptions.Mappers
{
    public sealed class IOExceptionMapper
    {
        public IOException Map(IOException exception)
        {
            // NOTE: ディスク容量不足の場合はDiskFullExceptionに変換する
            if (IsDiskFullException(exception))
            {
                return new DiskFullException(exception.Message, exception);
            }

            return exception;
        }

        bool IsDiskFullException(Exception exception)
        {
            return exception is IOException ioException &&
                   (ioException.Message.Contains("No space left on device") ||
                    ioException.Message.Contains("Disk full."));
        }
    }
}
