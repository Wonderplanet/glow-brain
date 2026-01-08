using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Localization
{
    public interface ILocalizationLocaleSelector
    {
        UniTask ChangeLocale(CancellationToken cancellationToken, string newLocaleName);
        string[] GetAvailableLocaleNames();
        void ReserveNewLocale(string newLocaleName);
    }
}
