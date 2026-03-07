using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views.StepUpGacha
{
    /// <summary>
    /// 71-1_ガシャ
    /// ステップアップガシャのコストやおまけ内容の表示コンポーネント
    /// </summary>  
    public class StepUpGachaComponent : UIObject
    {
        [SerializeField] List<StepUpGachaDetailComponent> _stepUpGachaDetailComponents;

        public void Setup(StepUpGachaViewModel viewModel)
        {
            var viewModels = viewModel.DetailViewModels;
            
            // View上のコンポーネント分設定する TODO: ステップ数が6以上になる場合は改修が必要
            for (var i = 0; i < _stepUpGachaDetailComponents.Count; i++)
            {
                if (i < viewModels.Count)
                {
                    _stepUpGachaDetailComponents[i].Setup(viewModels[i]);
                    _stepUpGachaDetailComponents[i].IsVisible = true;
                }
                else
                {
                    _stepUpGachaDetailComponents[i].IsVisible = false;
                }
            }
        }
    }
}