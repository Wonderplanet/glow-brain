namespace GLOW.Core.Domain.Models
{
    public record UserPlaySessionModel(int MstStageId)
    {
        public int MstStageId { get; } = MstStageId;
    }
}
