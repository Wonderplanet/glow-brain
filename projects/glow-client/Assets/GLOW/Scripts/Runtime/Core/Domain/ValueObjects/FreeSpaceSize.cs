using GLOW.Scenes.Title.Domains.Formatter;

namespace GLOW.Core.Domain.ValueObjects
{
    public record FreeSpaceSize(ulong Value)
    {
        public string ToStringSeparated()
        {
            return DataSizeFormatter.ConvertToStringMBOrMore(Value);
        }
    }
}
