using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;

namespace GLOW.Scenes.BoxGacha.Presentation.Translator
{
    public static class BoxGachaInfoViewModelTranslator
    {
        public static BoxGachaInfoViewModel ToBoxGachaInfoViewModel(BoxGachaInfoModel model)
        {
            var cellList = ToRewardListCellViewModelList(model.BoxGachaPrizes);
            
            return new BoxGachaInfoViewModel(
                model.TotalStockCount,
                model.BoxResetCount,
                model.CurrentBoxTotalDrawnCount,
                model.CurrentBoxLevel,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.CostResource),
                model.CostAmount,
                model.RemainingTimeSpan,
                cellList);
        }
        
        static IReadOnlyList<BoxGachaRewardListCellViewModel> ToRewardListCellViewModelList(
            IReadOnlyList<BoxGachaPrizeModel> boxGachaPrizes)
        {
            var cellList = new List<BoxGachaRewardListCellViewModel>();
            // 4つずつのグループ数を計算（切り上げ）
            var cellCount = (boxGachaPrizes.Count + 3) / 4;
            
            for (var i = 0; i < cellCount; i++)
            {
                var prizes = boxGachaPrizes
                    .Skip(i * 4)
                    .Take(4)
                    .ToList();
                
                // 足りない分はEmptyで埋める
                while (prizes.Count < 4)
                {
                    prizes.Add(BoxGachaPrizeModel.Empty);
                }
                
                var cellViewModel = prizes
                    .Select(ToPrizeCellViewModel)
                    .ToList();
                cellList.Add(new BoxGachaRewardListCellViewModel(cellViewModel));
            }

            return cellList;
        }
        
        static BoxGachaPrizeCellViewModel ToPrizeCellViewModel(BoxGachaPrizeModel model)
        {
            if(model.IsEmpty()) return BoxGachaPrizeCellViewModel.Empty;
            
            return new BoxGachaPrizeCellViewModel(
                model.IsPickUp,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.PrizeResource),
                model.DrawCount,
                model.Stock);
        }
    }
}