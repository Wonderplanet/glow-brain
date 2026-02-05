using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public interface IFontAssetClearExecutor
    {
        UniTask LoadFonts(CancellationToken cancellationToken);
        void UnloadAndClearFontAssetData();
    }

    // TMPフォントが実行時に生成するAtlasTextureをクリアするためのクラス
    // 基盤にAddressableDynamicTMPFontAssetCleanerが導入されたらそちらを使ったほうがいい
    public class FontAssetClearExecutor : IFontAssetClearExecutor
    {
        [Inject] IFontAssetLoader FontAssetLoader { get; }
        [Inject] IFontAssetContainer FontAssetContainer { get; }

        // Adressablesに登録されていて、生成されたフォントデータをクリアしたいフォントアセットをここに追加する
        // TODO: V1.1ではフォントをクリアしないように(不具合出たため)
        // フォントのクリアタイミングを画面遷移呼び出し前にすれば問題無くなる想定だが検証する時間がなかった
        List<FontAssetPath> FontAssetPath => new List<FontAssetPath>()
        {
            // new ("NotoSansJP-Black SDF.asset"),
        };

        async UniTask IFontAssetClearExecutor.LoadFonts(CancellationToken cancellationToken)
        {
            var loads = FontAssetPath
                .Select(key => FontAssetLoader.Load(cancellationToken, key));
            await UniTask.WhenAll(loads);
        }

        void IFontAssetClearExecutor.UnloadAndClearFontAssetData()
        {
            foreach (var path in FontAssetPath)
            {
                var font = FontAssetContainer.Get(path);
                font.ClearFontAssetData();
            }

            FontAssetLoader.Unload();
        }
    }
}
