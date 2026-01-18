using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class GachaAnimationLoadUseCase
    {
        [Inject] IGachaCacheRepository GachaCacheRepository { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IGachaAnimationUnitInfoLoader GachaAnimationUnitInfoLoader { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IBackgroundMusicManagement BackgroundMusicManagement { get; }

        public async UniTask LoadGachaAnimAsset(CancellationToken cancellationToken)
        {
            // キャッシュからガチャ結果を取得
            var resultCache = GachaCacheRepository.GetGachaResultModels();

            foreach (var model in resultCache)
            {
                if(model.ResourceType != ResourceType.Unit && model.PreConversionResource.ResourceType != ResourceType.Unit) continue;

                var masterDataId = model.ResourceId;

                if(model.PreConversionResource.ResourceType == ResourceType.Unit)
                {
                    masterDataId = model.PreConversionResource.ResourceId;
                }

                var mstCharacterData = MstCharacterDataRepository.GetCharacter(masterDataId);
                var unitAssetKey = mstCharacterData.AssetKey;
                var gachaAnimationUnitInfoAssetPath = GachaAnimationUnitInfoAssetPath.FromAssetKey(unitAssetKey);
                var unitImageAssetPath = UnitImageAssetPath.FromAssetKey(unitAssetKey);

                await GachaAnimationUnitInfoLoader.Load(cancellationToken, gachaAnimationUnitInfoAssetPath);
                await UnitImageLoader.Load(cancellationToken, unitImageAssetPath);
            }

            // ガシャ用BGMのロード
            await BackgroundMusicManagement.Load(cancellationToken, BGMAssetKeyDefinitions.BGM_gacha_animation);
        }
    }
}
