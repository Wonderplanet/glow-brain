using System;
using System.Text;

namespace GLOW.Modules.ArgumentURLEncrypter.Domain
{
    public static class RandomStringGenerator
    {
        const string Characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        public static string Generate(int length)
        {
            var random = new Random();
            var stringBuilder = new StringBuilder(length);

            for (int i = 0; i < length; i++)
            {
                var index = random.Next(Characters.Length);
                stringBuilder.Append(Characters[index]);
            }

            return stringBuilder.ToString();
        }
    }
}