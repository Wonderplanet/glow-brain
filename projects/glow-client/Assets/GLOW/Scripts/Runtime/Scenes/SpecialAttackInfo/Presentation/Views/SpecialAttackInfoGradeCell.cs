using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Views
{
    [Serializable]
    class GradeImageList
    {
        public int _unitGrade;
        public Sprite _rankImage;
    }

    public class SpecialAttackInfoGradeCell : UICollectionViewCell
    {
        [SerializeField] UIImage _gradeImage;
        [SerializeField] UIText _gradeText;

        [SerializeField] List<GradeImageList> _rankImageList;

        public void Setup(SpecialAttackInfoGradeViewModel viewModel)
        {
            if (viewModel == null)
            {
                return;
            }

            _gradeImage.Sprite = _rankImageList.Find(x => x._unitGrade == viewModel.UnitGrade.Value)._rankImage;
            _gradeText.SetText(viewModel.GradeDescription.Value);
        }
    }
}
