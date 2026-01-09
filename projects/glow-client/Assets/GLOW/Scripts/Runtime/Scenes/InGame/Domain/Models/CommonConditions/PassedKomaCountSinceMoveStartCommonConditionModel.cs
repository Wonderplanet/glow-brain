using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record PassedKomaCountSinceMoveStartCommonConditionModel(PassedKomaCount PassedKomaCount) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.PassedKomaCountSinceMoveStart;

        /// <summary> 移動開始時点から一定数のコマを移動したら </summary>
        public bool MeetsCondition(ICommonConditionContext context)
        {
            if (context.MyUnit.MoveStartedKoma.IsEmpty()) return false;
            
            var stoppedKomaNo = context.MstPage.GetKomaNo(context.MyUnit.MoveStartedKoma.Id);
            var locatedKomaNo = context.MstPage.GetKomaNo(context.MyUnit.LocatedKoma.Id);

            // コマは味方ゲートの右上が0始点で左下敵ゲートに向かってKomaNoが増えるため、敵側の場合は逆にする
            var passedKomaNum = context.MyUnit.BattleSide == BattleSide.Player
                ? locatedKomaNo - stoppedKomaNo
                : stoppedKomaNo - locatedKomaNo;

            return passedKomaNum >= PassedKomaCount;
        }
    }
}
