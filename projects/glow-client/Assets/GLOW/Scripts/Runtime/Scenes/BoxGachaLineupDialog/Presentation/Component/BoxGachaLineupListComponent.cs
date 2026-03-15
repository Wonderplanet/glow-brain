using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.Component
{
    public class BoxGachaLineupListComponent : UIObject
    {
        [SerializeField] List<BoxGachaLineupComponent> _lineupList;

        const int URIndex = 0;
        const int SSRIndex = 1;
        const int SRIndex = 2;
        const int RIndex = 3;
        
        public void SetUpURLineupList(
            BoxGachaLineupListViewModel boxGachaURLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            var lineupComponent = _lineupList[URIndex];
            SetUpLineupList(boxGachaURLineupListViewModel, lineupComponent, onPrizeIconSelected);
        }
        
        public void SetUpSSRLineupList(
            BoxGachaLineupListViewModel boxGachaSSRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            var lineupComponent = _lineupList[SSRIndex];
            SetUpLineupList(boxGachaSSRLineupListViewModel, lineupComponent, onPrizeIconSelected);
        }
        
        public void SetUpSRLineupList(
            BoxGachaLineupListViewModel boxGachaSRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            var lineupComponent = _lineupList[SRIndex];
            SetUpLineupList(boxGachaSRLineupListViewModel, lineupComponent, onPrizeIconSelected);
        }
        
        public void SetUpRLineupList(
            BoxGachaLineupListViewModel boxGachaRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            var lineupComponent = _lineupList[RIndex];
            SetUpLineupList(boxGachaRLineupListViewModel, lineupComponent, onPrizeIconSelected);
        }
        
        void SetUpLineupList(
            BoxGachaLineupListViewModel boxGachaLineupListViewModel, 
            BoxGachaLineupComponent lineupComponent,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            if (boxGachaLineupListViewModel.LineupCells.Count == 0)
            {
                lineupComponent.IsVisible = false;
                return;
            }
            
            lineupComponent.SetupHeaderText(
                boxGachaLineupListViewModel.Rarity, 
                boxGachaLineupListViewModel.LineupCells.Count);

            for (var i = 0; i < boxGachaLineupListViewModel.LineupCells.Count; i++)
            {
                var viewModel = boxGachaLineupListViewModel.LineupCells[i];
                var lineupCell = lineupComponent.InstantiateCell();
                // セルのセットアップ
                // 交互に背景色を変える
                lineupCell.SetUpBackground(i % 2 == 0);
                lineupCell.SetUpNameText(viewModel.PrizeName);
                lineupCell.SetUpPlayerResourceIcon(viewModel.PrizeIconViewModel, onPrizeIconSelected);
                lineupCell.SetUpStockCountText(viewModel.PrizeStock);
            }
        }
    }
}