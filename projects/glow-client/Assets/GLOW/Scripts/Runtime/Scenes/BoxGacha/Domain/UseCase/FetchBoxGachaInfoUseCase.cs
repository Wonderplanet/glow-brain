using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.BoxGacha.Domain.Factory;
using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGacha.Domain.Provider;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.UseCase
{
    public class FetchBoxGachaInfoUseCase
    {
        [Inject] IBoxGachaService BoxGachaService { get; }
        [Inject] IUserBoxGachaCacheRepository UserBoxGachaCacheRepository { get; }
        [Inject] IMstBoxGachaProvider MstBoxGachaProvider { get; }
        [Inject] IBoxGachaInfoModelFactory BoxGachaInfoModelFactory { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public async UniTask<BoxGachaTopModel> CacheAndShowBoxGachaInfo(
            MasterDataId mstEventId, 
            CancellationToken cancellationToken)
        {
            var mstBoxGachaModel = MstBoxGachaProvider.GetMstBoxGachaModelByEventId(mstEventId);
            if (mstBoxGachaModel.IsEmpty()) return BoxGachaTopModel.Empty;

            var userBoxGachaModel = UserBoxGachaCacheRepository.GetFirstOrDefault(mstBoxGachaModel.Id);
            if (userBoxGachaModel.IsEmpty())
            {
                // 初回取得: サーバーから情報を取得
                var result = await BoxGachaService.Info(cancellationToken, mstBoxGachaModel.Id);

                // 副作用: キャッシュを更新
                UserBoxGachaCacheRepository.Save(result.UserBoxGachaModel);
                userBoxGachaModel = result.UserBoxGachaModel;
            }

            var infoModel = BoxGachaInfoModelFactory.Create(
                mstEventId,
                mstBoxGachaModel.Id,
                mstBoxGachaModel.CostId,
                mstBoxGachaModel.CostAmount,
                userBoxGachaModel);
            
            var decoUnitFirst = MstCharacterDataRepository.GetCharacter(mstBoxGachaModel.MstDisplayUnitIdFirst);
            var decoUnitSecond = MstCharacterDataRepository.GetCharacter(mstBoxGachaModel.MstDisplayUnitIdSecond);

            var model = new BoxGachaTopModel(
                mstBoxGachaModel.Id,
                mstBoxGachaModel.Name,
                UnitImageAssetPath.FromAssetKey(decoUnitFirst.AssetKey), 
                UnitImageAssetPath.FromAssetKey(decoUnitSecond.AssetKey),
                KomaBackgroundAssetPath.FromAssetKey(mstBoxGachaModel.KomaBackgroundAssetKey),
                infoModel);

            return model;
        }
    }
}