namespace GLOW.Debugs.AdminDebug.Domain.Models
{
    public record AdminDebugMenuCommandModel(string Command, string Name, string Description)
    {
        public string Command { get; } = Command;
        public string Name { get; } = Name;
        public string Description { get; } = Description;
    }
    public record AdminDebugMenuCommandListModel(AdminDebugMenuCommandModel[] DebugMenuCommandModels)
    {
        public AdminDebugMenuCommandModel[] DebugMenuCommandModels { get; } = DebugMenuCommandModels;
    }
}
