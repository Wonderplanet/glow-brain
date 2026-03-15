using System;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeContentsView : UIView
    {
        [SerializeField] ArtworkGradeContentCell _cellPrefab;
        [SerializeField] Transform _contentParent;

        public void SetUpView(ArtworkGradeContentsViewModel viewModel, Action<PlayerResourceIconViewModel> onIconTapped)
        {
            foreach(var model in viewModel.CellViewModels)
            {
                var cell = Instantiate(_cellPrefab, _contentParent);
                cell.Setup(model, onIconTapped);
            }
        }
    }
}
