using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using System.Linq;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Evaluator
{
    public class PvpResultEvaluator : IPvpResultEvaluator
    {
        [Inject] IInGameScene InGameScene { get; }

        public enum PvpResultType
        {
            Victory,
            Defeat,
        }
        
        public enum PvpFinishType
        {
            OutPostHpZero,
            MaxDistance,
        }

        public PvpResultModel Evaluate()
        {
            var scene = InGameScene;

            // ギブアップしていたら問答無用で敗北
            if (scene.IsBattleGiveUp)
            {
                return new PvpResultModel(
                    PvpResultType.Defeat,
                    PvpMaxDistanceRatio.Empty,
                    PvpMaxDistanceRatio.One,
                    PvpFinishType.OutPostHpZero);
            }

            // HPでの勝敗判定。もし両方のゲートのHPが0ならば、距離での勝敗判定に移行する
            var isEnemyOutpostHpZero = scene.EnemyOutpost.Hp.IsZero();
            var isPlayerOutpostHpZero = scene.PlayerOutpost.Hp.IsZero();
            if (isEnemyOutpostHpZero && !isPlayerOutpostHpZero)
            {
                return new PvpResultModel(
                    PvpResultType.Victory,
                    PvpMaxDistanceRatio.One,
                    PvpMaxDistanceRatio.Empty,
                    PvpFinishType.OutPostHpZero);
            }

            if (isPlayerOutpostHpZero && !isEnemyOutpostHpZero)
            {
                return new PvpResultModel(
                    PvpResultType.Defeat,
                    PvpMaxDistanceRatio.Empty,
                    PvpMaxDistanceRatio.One,
                    PvpFinishType.OutPostHpZero);
            }

            var playerUnits = scene.CharacterUnits
                .Where(u => u.BattleSide == BattleSide.Player)
                .ToList();
            var enemyUnits = scene.CharacterUnits
                .Where(u => u.BattleSide == BattleSide.Enemy)
                .ToList();

            // プレイヤーと対戦相手双方ユニット無しならそれぞれ半分の比率を渡し、プレイヤー側が勝利とする
            if (playerUnits.Count <= 0 && enemyUnits.Count <= 0)
            {
                return new PvpResultModel(
                    PvpResultType.Victory,
                    new PvpMaxDistanceRatio(0.5f),
                    new PvpMaxDistanceRatio(0.5f),
                    PvpFinishType.MaxDistance);
            }

            if(playerUnits.Count > 0 && enemyUnits.Count <= 0)
            {
                return new PvpResultModel(
                    PvpResultType.Victory,
                    PvpMaxDistanceRatio.One,
                    PvpMaxDistanceRatio.Empty,
                    PvpFinishType.MaxDistance);
            }

            if(playerUnits.Count <= 0 && enemyUnits.Count > 0)
            {
                return new PvpResultModel(
                    PvpResultType.Defeat,
                    PvpMaxDistanceRatio.Empty,
                    PvpMaxDistanceRatio.One,
                    PvpFinishType.MaxDistance);
            }

            // 距離判定
            var playerMaxDist = playerUnits.Max(u => u.Pos.X);
            var enemyMaxDist = enemyUnits.Max(u => u.Pos.X);

            // 全く同じ距離の時はプレイヤー側を勝利とする
            var resultType = playerMaxDist >= enemyMaxDist ? PvpResultType.Victory : PvpResultType.Defeat;

            // プレイヤーと対戦相手の距離を合わせて1とした上で、プレイヤーと対戦相手の距離比率を計算
            var totalDistance = playerMaxDist + enemyMaxDist;
            var playerDistanceRatio = playerMaxDist / totalDistance;
            var enemyDistanceRatio = enemyMaxDist / totalDistance;

            return new PvpResultModel(
                resultType,
                new PvpMaxDistanceRatio(playerDistanceRatio),
                new PvpMaxDistanceRatio(enemyDistanceRatio),
                PvpFinishType.MaxDistance);
        }
    }
}
