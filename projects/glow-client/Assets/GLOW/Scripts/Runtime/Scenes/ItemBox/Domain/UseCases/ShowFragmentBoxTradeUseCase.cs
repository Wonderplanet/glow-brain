using GLOW.Core.Domain.Models;
using GLOW.Scenes.ItemBox.Domain.Factory;
using GLOW.Scenes.ItemBox.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class ShowFragmentBoxTradeUseCase
    {
        [Inject] IFragmentBoxTradeModelFactory FragmentBoxTradeModelFactory { get; }
        
        public FragmentBoxTradeModel GetFragmentBoxTradeModel(ItemModel itemModel)
        {
            return FragmentBoxTradeModelFactory.CreateFragmentBoxTradeModel(itemModel.Id);
        }
    }
}