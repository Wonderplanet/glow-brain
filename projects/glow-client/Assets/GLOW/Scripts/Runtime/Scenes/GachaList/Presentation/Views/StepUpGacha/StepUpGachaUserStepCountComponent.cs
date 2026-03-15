using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Domain.Model;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views.StepUpGacha
{
    /// <summary>
    /// 71-1_ガシャ
    /// ステップアップガシャ下部のステップ進行度表示コンポーネント
    /// </summary>  
    public class StepUpGachaUserStepCountComponent : UIObject
    {
        [SerializeField] List<StepUpGachaStepComponent> _stepUpGachaStepComponents;
        
        public void Setup(StepUpStepCount stepUpStepCount)
        {
            // 全ステップ数に合わせたオブジェクト表示
            //　現在ステップ強調表示、通過済みステップをグレーアウト
            var maxStepCount = stepUpStepCount.MaxStepNumber;
            var maxStepCountIndex = maxStepCount - 1; // ステップは1始まりの想定なので、インデックスに変換
            var currentStepCount = stepUpStepCount.CurrentStepNumber;
            var currentStepCountIndex = currentStepCount - 1; // ステップは1始まりの想定なので、インデックスに変換
            for (var i = 0; i < _stepUpGachaStepComponents.Count; i++)
            {
                var stepComponent = _stepUpGachaStepComponents[i];
                
                // ステップ表示が最大ステップ数を超える場合は非表示
                if (i <= maxStepCountIndex)
                {
                    stepComponent.IsVisible = true; // 表示
                }
                else
                {
                    stepComponent.IsVisible = false; // 非表示
                    continue;
                }
                
                var isSelected = i == currentStepCountIndex;
                var isGrayOut = i < currentStepCountIndex; 
                
                stepComponent.Setup(isSelected, isGrayOut);
            }
        }
    }
}