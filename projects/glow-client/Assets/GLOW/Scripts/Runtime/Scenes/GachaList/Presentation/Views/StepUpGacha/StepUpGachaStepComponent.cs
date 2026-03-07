using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views.StepUpGacha
{
    /// <summary>
    /// 71-1_ガシャ
    /// ステップアップガシャ下部のステップ進行度の1ステップ分のコンポーネント
    /// </summary>  
    public class StepUpGachaStepComponent : UIObject
    {
        [SerializeField] UIObject _selectStepObject;
        [SerializeField] UIObject _stepGrayOutObject;

        public void Setup(bool isSelected, bool isGrayOut)
        {
            _selectStepObject.IsVisible = isSelected;
            _stepGrayOutObject.IsVisible = isGrayOut;
        }
    }
}