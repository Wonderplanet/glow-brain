using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.Factory;
using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGacha.Domain.Provider;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.UseCase
{
    public class ResetBoxGachaUseCase
    {
        [Inject] IBoxGachaService BoxGachaService { get; }
        [Inject] IUserBoxGachaCacheRepository UserBoxGachaCacheRepository { get; }
        [Inject] IMstBoxGachaProvider MstBoxGachaProvider { get; }
        [Inject] IBoxGachaInfoModelFactory BoxGachaInfoModelFactory { get; }
        
        public async UniTask<BoxGachaInfoModel> Reset(MasterDataId mstEventId, CancellationToken cancellationToken)
        {
            var mstBoxGachaModel = MstBoxGachaProvider.GetMstBoxGachaModelByEventId(mstEventId);
            if (mstBoxGachaModel.IsEmpty()) return BoxGachaInfoModel.Empty;

            var userBoxGachaModel = UserBoxGachaCacheRepository.GetFirstOrDefault(mstBoxGachaModel.Id);
            if (userBoxGachaModel.IsEmpty()) return BoxGachaInfoModel.Empty;
            
            var result = await BoxGachaService.Reset(
                cancellationToken, 
                mstBoxGachaModel.Id,
                userBoxGachaModel.CurrentBoxLevel);
            
            // 副作用: キャッシュを更新
            UserBoxGachaCacheRepository.Save(result.UserBoxGachaModel);
            userBoxGachaModel = result.UserBoxGachaModel;
            
            var model = BoxGachaInfoModelFactory.Create(
                mstEventId,
                mstBoxGachaModel.Id,
                mstBoxGachaModel.CostId,
                mstBoxGachaModel.CostAmount,
                userBoxGachaModel);
            
            return model;
        }
    }
}