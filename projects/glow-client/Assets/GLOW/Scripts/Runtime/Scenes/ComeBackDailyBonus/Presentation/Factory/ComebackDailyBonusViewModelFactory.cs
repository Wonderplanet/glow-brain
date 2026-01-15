using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ComeBackDailyBonus.Domain.Model;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.Translator;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel;

namespace GLOW.Scenes.ComeBackDailyBonus.Presentation.Factory
{
    public class ComebackDailyBonusViewModelFactory : IComebackDailyBonusViewModelFactory
    {
        const int CellsPerRow = 4;
        
        public ComebackDailyBonusViewModel Create(ComebackDailyBonusModel model)
        {
            var cellViewModels = model.ComebackDailyBonusCellModels
                .Select(ComebackDailyBonusCellViewModelTranslator.ToViewModel)
                .ToList();
            
            // 4の倍数になるように空セルを追加
            var lastCellViewModel = cellViewModels.MaxBy(cell => cell.LoginDayCount.Value);
            var emptyCells = CreateComebackDailyBonusEmptyCellModels(lastCellViewModel.LoginDayCount);
            cellViewModels = cellViewModels.Concat(emptyCells).ToList();
            
            var resourceViewModels = model.CommonReceiveResourceModels
                .Select(resourceModel => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(resourceModel))
                .ToList();
            
            return new ComebackDailyBonusViewModel(
                model.LoginDayCount,
                cellViewModels,
                resourceViewModels,
                model.RemainingTime);
        }
        
        IReadOnlyList<DailyBonusCollectionCellViewModel> CreateComebackDailyBonusEmptyCellModels(LoginDayCount lastLoginDayCount)
        {
            var surplus = lastLoginDayCount % CellsPerRow;
            
            // 4で割り切れる場合は空セルを追加しない
            if (surplus.IsZero()) return new List<DailyBonusCollectionCellViewModel>();
            
            var needCellCount = new LoginDayCount(CellsPerRow) - surplus;
            
            var needCellModels = new List<DailyBonusCollectionCellViewModel>();
            for (var i = 0; i < needCellCount.Value; i++)
            {
                var emptyCellModel = new DailyBonusCollectionCellViewModel(
                    DailyBonusReceiveStatus.Nothing,
                    lastLoginDayCount + i + 1,
                    PlayerResourceIconViewModel.Empty);
                needCellModels.Add(emptyCellModel);
            }
            
            return needCellModels;
        }
    }
}