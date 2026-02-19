using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaCostItemDetailView.Domain.Models;
using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.GachaCostItemDetailView.Domain.UseCases
{
    public class GachaCostItemDetailUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public GachaCostItemDetailUseCaseModel GetUseCaseModelById(MasterDataId mstCostId)
        {
            var userItemModel = GameRepository.GetGameFetchOther().UserItemModels.
                FirstOrDefault(m => m.MstItemId == mstCostId, UserItemModel.Empty);
            var resourceAmount = userItemModel.Amount.ToPlayerResourceAmount();
            var playerResourceModel = PlayerResourceModelFactory.Create(ResourceType.Item, mstCostId, resourceAmount);
            
            // ガシャの開催状態とチケット所持状態の確認
            var now = TimeProvider.Now;

            // 開催中ガシャ取得
            var activeGacha = OprGachaRepository.GetOprGachaModelsByDataTime(now);
            
            // コストから消費先ガシャが開催しているか取得
            var isTransition = OprGachaUseResourceRepository.GetOprGachaUseResourceModelsByItemId(mstCostId)
                .Select(m => m.OprGachaId)
                .Distinct()
                .Any(id => activeGacha.Any(a => a.Id == id));
            
            // ガシャ開催中
            return new GachaCostItemDetailUseCaseModel(
                playerResourceModel,
                isTransition ? TransitionButtonGrayOutFlag.False : TransitionButtonGrayOutFlag.True);
        }
    }
}