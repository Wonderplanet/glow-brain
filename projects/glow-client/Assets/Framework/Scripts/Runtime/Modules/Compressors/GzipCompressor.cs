using System.IO;
using System.IO.Compression;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Compressors
{
    public static class GzipCompressor
    {
        public static async UniTask CompressFileAsync(string sourceFile, string destinationFile)
        {
            await using var sourceStream = new FileStream(sourceFile, FileMode.Open);
            await using var destinationStream = new FileStream(destinationFile, FileMode.Create);
            // NOTE: 書き込み完了後にStreamを閉じるる必要があるためusingブロックを利用
            using (var gzipStream = new GZipStream(destinationStream, CompressionMode.Compress))
            {
                await sourceStream.CopyToAsync(gzipStream);
            }
        }

        public static async UniTask<byte[]> CompressAsync(byte[] data)
        {
            using var outputStream = new MemoryStream();
            // NOTE: 書き込み完了後にStreamを閉じるる必要があるためusingブロックを利用
            using (var gzipStream = new GZipStream(outputStream, CompressionMode.Compress))
            {
                await gzipStream.WriteAsync(data, 0, data.Length);
            }
            return outputStream.ToArray();
        }

        public static byte[] Compress(byte[] data)
        {
            using var outputStream = new MemoryStream();
            // NOTE: 書き込み完了後にStreamを閉じるる必要があるためusingブロックを利用
            using (var gzipStream = new GZipStream(outputStream, CompressionMode.Compress))
            {
                gzipStream.Write(data, 0, data.Length);
            }

            return outputStream.ToArray();
        }

        public static async UniTask DecompressFileAsync(string sourceFile, string destinationFile)
        {
            await using var sourceStream = new FileStream(sourceFile, FileMode.Open);
            await using var destinationStream = new FileStream(destinationFile, FileMode.Create);
            await using var gzipStream = new GZipStream(sourceStream, CompressionMode.Decompress);
            await gzipStream.CopyToAsync(destinationStream);
        }

        public static async UniTask<byte[]> DecompressAsync(byte[] data)
        {
            using var inputStream = new MemoryStream(data);
            using var outputStream = new MemoryStream();
            await using var gzipStream = new GZipStream(inputStream, CompressionMode.Decompress);
            await gzipStream.CopyToAsync(outputStream);
            return outputStream.ToArray();
        }

        public static byte[] Decompress(byte[] data)
        {
            using var inputStream = new MemoryStream(data);
            using var outputStream = new MemoryStream();
            using var gzipStream = new GZipStream(inputStream, CompressionMode.Decompress);
            gzipStream.CopyTo(outputStream);
            return outputStream.ToArray();
        }
    }
}
