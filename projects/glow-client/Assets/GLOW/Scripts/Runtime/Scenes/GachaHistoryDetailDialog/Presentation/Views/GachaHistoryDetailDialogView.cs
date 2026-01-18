using System.Collections.Generic;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views
{
    public class GachaHistoryDetailDialogView : UIView
    {
        [SerializeField] GachaHistoryCell _cell;
        [SerializeField] List<GachaHistoryDetailCell> _detailCells;
        
        public void Setup(
            GachaHistoryCellViewModel cellViewModel,
            IReadOnlyList<GachaHistoryDetailCellViewModel> detailCellViewModels)
        {
            _cell.Setup(cellViewModel, () => { });
            
            for (int i = 0; i < _detailCells.Count; i++)
            {
                if (i < detailCellViewModels.Count)
                {
                    _detailCells[i].Hidden = false;
                    _detailCells[i].gameObject.SetActive(true);
                    _detailCells[i].Setup(detailCellViewModels[i]);
                }
                else
                {
                    _detailCells[i].Hidden = true;
                }
            }
        }
    }
}