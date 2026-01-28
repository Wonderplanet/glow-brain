using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BoxGacha.Presentation.Component
{
    public class BoxGachaRewardListCellComponent : UICollectionViewCell
    {
        [SerializeField] List<BoxGachaRewardIconComponent> _rewardIconComponents;
        
        public void SetUpCell(
            BoxGachaRewardListCellViewModel cellViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconTapped)
        {
            for (var i = 0; i < _rewardIconComponents.Count; i++)
            {
                // リストに要素がない場合は非表示にする
                if (cellViewModel.PrizeCellViewModelList.Count <= i)
                {
                    _rewardIconComponents[i].IsVisible = false;
                    continue;
                }
                
                if (cellViewModel.PrizeCellViewModelList[i].IsEmpty())
                {
                    _rewardIconComponents[i].IsVisible = false;
                    continue;
                }
                
                _rewardIconComponents[i].IsVisible = true;
                _rewardIconComponents[i].SetUpRewardIcon(
                    cellViewModel.PrizeCellViewModelList[i],
                    onPrizeIconTapped);
            }
        }
    }
}