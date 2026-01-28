namespace GLOW.Scenes.InGame.Domain.Constants
{
    public enum InGameCommonConditionType
    {
        None,
        Always,
        MyHpLessThanOrEqualPercentage,
        MyHpMoreThanOrEqualPercentage,
        MyDamage,
        EnemyOutpostDamage,
        EnemyUnitDead,
        DeadEnemyUnitCount,
        StageTime,
        ElapsedTimeSinceSummoned,
        StageReady,
        PlayerUnitEnterSpecificKoma,
        PlayerUnitEnterSameKoma,
        DarknessKomaCleared,
        EnemyUnitTransformed,                   //変身したら
        EnemyUnitTransformDead,                 //変身した敵が死んだら
        EnemySequenceElementActivated,
        ElapsedTimeSinceEnemySequenceGroupActivated, //シーケンスグループの特定時間経過
        EnemyUnitSummoned,                      // 指定IDの敵が出現したら,
        EnemyUnitTargetPosition,                // 指定の座標位置に到達したら
        PassedKomaCountSinceMoveStart,          // 移動を開始してから指定数分のコマを移動したら
        ElapsedTimeSinceMoveStopped,            // 移動を停止してから経過した時間
        ElapsedTimeSinceMoveStarted,            // 移動を開始してから経過した時間
        EnemyOutpostHpPercentage,               // 敵ゲートHPが指定%以下になったら
    }
}
