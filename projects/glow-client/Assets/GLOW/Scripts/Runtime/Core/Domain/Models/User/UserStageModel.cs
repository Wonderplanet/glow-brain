namespace GLOW.Core.Domain.Models
{
    public record UserStageModel(int StageId, long Score)
    {
        public int StageId { get; } = StageId;
        public long Score { get; } = Score;
    }
}
