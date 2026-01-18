using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.Localization.Tables;

namespace WPFramework.Modules.Localization
{
    public interface ILocalizationAssetSource
    {
        UniTask<StringTable> GetStringTable(CancellationToken cancellationToken, string tableReference);
    }
}
