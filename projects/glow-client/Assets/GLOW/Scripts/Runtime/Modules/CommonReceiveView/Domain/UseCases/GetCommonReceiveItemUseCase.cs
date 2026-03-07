using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Domain.UseCases
{
    public class GetCommonReceiveItemUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        // TODO : 色々なところに同じような処理があるので統一したい
        public PlayerResourceModel GetPlayerResource(ResourceType type, MasterDataId id, PlayerResourceAmount amount)
        {
            return PlayerResourceModelFactory.Create(type, id, amount);
        }
    }
}
