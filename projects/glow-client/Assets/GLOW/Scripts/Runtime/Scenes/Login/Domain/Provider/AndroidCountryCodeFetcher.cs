#if UNITY_ANDROID
using System.Threading;
using UnityEngine;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Login.Domain.Provider
{
    public class AndroidCountryCodeFetcher
    {
        public static async UniTask<string> FetchAsync(CancellationToken cancellationToken)
        {
            using (var unityPlayer = new AndroidJavaClass("com.unity3d.player.UnityPlayer"))
            using (var activity = unityPlayer.GetStatic<AndroidJavaObject>("currentActivity"))
            using (var paramsBuilder = new AndroidJavaClass("com.android.billingclient.api.PendingPurchasesParams")
                .CallStatic<AndroidJavaObject>("newBuilder"))
            {
                // 1. PendingPurchasesParams の作成
                using (paramsBuilder.Call<AndroidJavaObject>("enableOneTimeProducts"))
                {
                    // enableOneTimeProductsの戻り値を解放
                }

                using (var pendingParams = paramsBuilder.Call<AndroidJavaObject>("build"))
                using (var builder = new AndroidJavaClass("com.android.billingclient.api.BillingClient")
                    .CallStatic<AndroidJavaObject>("newBuilder", activity))
                {
                    // 2. BillingClient のビルド
                    // IllegalArgumentException回避のため、空のリスナーを設定
                    using (builder.Call<AndroidJavaObject>("setListener", new EmptyPurchasesUpdatedListener()))
                    {
                        // setListenerの戻り値を解放
                    }

                    // PendingPurchases の設定
                    using (builder.Call<AndroidJavaObject>("enablePendingPurchases", pendingParams))
                    {
                        // enablePendingPurchasesの戻り値を解放
                    }

                    using (var client = builder.Call<AndroidJavaObject>("build"))
                    {
                        var utcs = new UniTaskCompletionSource<string>();

                        // キャンセルトークンの登録
                        using (cancellationToken.Register(() => utcs.TrySetCanceled(cancellationToken)))
                        {
                            // ストア接続開始
                            client.Call("startConnection", new BillingConnectionListener(client, utcs, cancellationToken));

                            return await utcs.Task;
                        }
                    }
                }
            }
        }

        /// <summary>
        /// BillingClient起動に必須となる空の購入リスナー
        /// </summary>
        class EmptyPurchasesUpdatedListener : AndroidJavaProxy
        {
            public EmptyPurchasesUpdatedListener()
                : base("com.android.billingclient.api.PurchasesUpdatedListener") { }

            // Javaのメソッド名は小文字開始なのでそれに合わせる
            void onPurchasesUpdated(AndroidJavaObject billingResult, AndroidJavaObject purchases)
            {
                // 国コード取得のみのため、何もしないが、リソースは解放する
                billingResult?.Dispose();
                purchases?.Dispose();
            }
        }

        class BillingConnectionListener : AndroidJavaProxy
        {
            readonly AndroidJavaObject _client;
            readonly UniTaskCompletionSource<string> _utcs;
            readonly CancellationToken _cancellationToken;

            public BillingConnectionListener(
                AndroidJavaObject client,
                UniTaskCompletionSource<string> utcs,
                CancellationToken cancellationToken)
                : base("com.android.billingclient.api.BillingClientStateListener")
            {
                _client = client;
                _utcs = utcs;
                _cancellationToken = cancellationToken;
            }

            // Java側のメソッド名「onBillingSetupFinished」に合わせる
            void onBillingSetupFinished(AndroidJavaObject billingResult)
            {
                using (billingResult)
                {
                    // キャンセル確認
                    if (_cancellationToken.IsCancellationRequested)
                    {
                        _utcs.TrySetCanceled(_cancellationToken);
                        return;
                    }

                    if (billingResult.Call<int>("getResponseCode") == 0) // OK
                    {
                        using (var paramsBuilder = new AndroidJavaClass("com.android.billingclient.api.GetBillingConfigParams")
                            .CallStatic<AndroidJavaObject>("newBuilder"))
                        using (var paramsObj = paramsBuilder.Call<AndroidJavaObject>("build"))
                        {
                            _client.Call("getBillingConfigAsync", paramsObj, new BillingConfigListener(_utcs, _cancellationToken));
                        }
                    }
                    else
                    {
                        _utcs.TrySetResult("");
                    }
                }
            }

            void onBillingServiceDisconnected()
            {
                // 接続が切断された場合はエラーとして扱う
                _utcs.TrySetResult("");
            }
        }

        class BillingConfigListener : AndroidJavaProxy
        {
            readonly UniTaskCompletionSource<string> _utcs;
            readonly CancellationToken _cancellationToken;

            public BillingConfigListener(UniTaskCompletionSource<string> utcs, CancellationToken cancellationToken)
                : base("com.android.billingclient.api.BillingConfigResponseListener")
            {
                _utcs = utcs;
                _cancellationToken = cancellationToken;
            }

            void onBillingConfigResponse(AndroidJavaObject billingResult, AndroidJavaObject billingConfig)
            {
                // メインスレッドに切り替えてから Result をセットする
                UniTask.Post(() =>
                {
                    using (billingResult)
                    using (billingConfig)
                    {
                        // キャンセル確認
                        if (_cancellationToken.IsCancellationRequested)
                        {
                            _utcs.TrySetCanceled(_cancellationToken);
                            return;
                        }

                        if (billingResult.Call<int>("getResponseCode") == 0 && billingConfig != null)
                        {
                            _utcs.TrySetResult(billingConfig.Call<string>("getCountryCode"));
                        }
                        else
                        {
                            _utcs.TrySetResult("");
                        }
                    }
                });
            }
        }
    }
}
#endif
