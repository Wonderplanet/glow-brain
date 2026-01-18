using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record OutpostViewInfoAssetPath(string Value)
    {
        public static OutpostViewInfoAssetPath Empty { get; } = new (string.Empty);

        public static OutpostViewInfoAssetPath FromAssetKey(OutpostAssetKey key)
        {
            return new OutpostViewInfoAssetPath(ZString.Format("outpost_view_info_{0}", key.Value));
        }
    }
}
