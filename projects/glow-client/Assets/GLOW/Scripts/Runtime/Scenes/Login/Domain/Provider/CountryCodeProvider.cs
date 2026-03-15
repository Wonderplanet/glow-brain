using System.Threading;
using System.Runtime.InteropServices;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Login.Domain.Provider
{
    public class CountryCodeProvider : ICountryCodeProvider
    {
#if UNITY_IOS && !UNITY_EDITOR
    [DllImport("__Internal")]
    static extern string GetStorefrontCountryCode();
#endif

        /// <summary>
        /// ストアの国コードを非同期（UniTask）で取得します
        /// </summary>
        async UniTask<string> ICountryCodeProvider.GetCountryCodeAsync(CancellationToken cancellationToken)
        {
#if UNITY_EDITOR
            await UniTask.Yield(); // 非同期性を保つため
            return "JPN";
#elif UNITY_IOS
        // iOSは同期で取得可能だがインターフェースを合わせる
        await UniTask.Yield();
        string code = GetStorefrontCountryCode();
        return string.IsNullOrEmpty(code) ? "" : code;
#elif UNITY_ANDROID
        // Androidのネイティブ非同期処理をawait
        return await AndroidCountryCodeFetcher.FetchAsync(cancellationToken);
#else
        await UniTask.Yield();
        return "";
#endif
        }
    }
}
